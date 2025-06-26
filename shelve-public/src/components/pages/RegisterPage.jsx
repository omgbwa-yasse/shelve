import React, { useState, useContext } from 'react';
import styled from 'styled-components';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import AuthContext from '../../context/AuthContext.js';

const RegisterContainer = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  padding: ${props => props.theme.spacing.md};
`;

const RegisterCard = styled.div`
  background: ${props => props.theme.colors.white};
  padding: ${props => props.theme.spacing.xxl};
  border-radius: ${props => props.theme.borderRadius.lg};
  box-shadow: ${props => props.theme.shadows.lg};
  width: 100%;
  max-width: 500px;
`;

const Title = styled.h1`
  text-align: center;
  margin-bottom: ${props => props.theme.spacing.xl};
  color: ${props => props.theme.colors.primary};
  font-size: 2rem;
  font-weight: 600;
`;

const Form = styled.form`
  display: flex;
  flex-direction: column;
  gap: ${props => props.theme.spacing.lg};
`;

const FormRow = styled.div`
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: ${props => props.theme.spacing.md};

  @media (max-width: ${props => props.theme.breakpoints.mobile}) {
    grid-template-columns: 1fr;
  }
`;

const FormGroup = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${props => props.theme.spacing.sm};
`;

const Label = styled.label`
  font-weight: 500;
  color: ${props => props.theme.colors.dark};
`;

const Input = styled.input`
  padding: ${props => props.theme.spacing.md};
  border: 2px solid ${props => props.theme.colors.light};
  border-radius: ${props => props.theme.borderRadius.md};
  font-size: 1rem;
  transition: border-color 0.3s ease;

  &:focus {
    outline: none;
    border-color: ${props => props.theme.colors.primary};
  }

  &:invalid {
    border-color: ${props => props.theme.colors.danger};
  }
`;

const Button = styled.button`
  padding: ${props => props.theme.spacing.md} ${props => props.theme.spacing.lg};
  background: ${props => props.theme.colors.primary};
  color: ${props => props.theme.colors.white};
  border: none;
  border-radius: ${props => props.theme.borderRadius.md};
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s ease;

  &:hover:not(:disabled) {
    background: ${props => props.theme.colors.primary};
    filter: brightness(0.9);
  }

  &:disabled {
    background: ${props => props.theme.colors.secondary};
    cursor: not-allowed;
  }
`;

const LinkContainer = styled.div`
  text-align: center;
  margin-top: ${props => props.theme.spacing.lg};
`;

const StyledLink = styled(Link)`
  color: ${props => props.theme.colors.primary};
  text-decoration: none;
  font-weight: 500;

  &:hover {
    text-decoration: underline;
  }
`;

const ErrorMessage = styled.div`
  background: ${props => props.theme.colors.danger};
  color: ${props => props.theme.colors.white};
  padding: ${props => props.theme.spacing.md};
  border-radius: ${props => props.theme.borderRadius.md};
  margin-bottom: ${props => props.theme.spacing.lg};
  text-align: center;
`;

const PasswordRequirements = styled.div`
  background: ${props => props.theme.colors.light};
  padding: ${props => props.theme.spacing.md};
  border-radius: ${props => props.theme.borderRadius.md};
  font-size: 0.9rem;
  color: ${props => props.theme.colors.secondary};
`;

const RegisterPage = () => {
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    confirmPassword: '',
    phone: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const { register } = useContext(AuthContext);
  const navigate = useNavigate();

  // Vérifier que le contexte est bien chargé
  if (!register) {
    console.error('La fonction register n\'est pas disponible dans le contexte');
    return <div>Erreur: Contexte d'authentification non disponible</div>;
  }

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear error when user starts typing
    if (error) setError('');
  };

  const validatePassword = (password) => {
    const requirements = [
      { test: password.length >= 8, message: 'Au moins 8 caractères' },
      { test: /[A-Z]/.test(password), message: 'Au moins une majuscule' },
      { test: /[a-z]/.test(password), message: 'Au moins une minuscule' },
      { test: /\d/.test(password), message: 'Au moins un chiffre' }
    ];

    return requirements.filter(req => !req.test);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Empêcher les soumissions multiples
    if (loading) return;

    setLoading(true);
    setError('');

    console.log('=== DÉBUT DE L\'INSCRIPTION ===');
    console.log('Données du formulaire:', formData);

    // Validation côté client
    if (!formData.firstName.trim() || !formData.lastName.trim() || !formData.email.trim() || !formData.password) {
      setError('Tous les champs obligatoires doivent être remplis');
      setLoading(false);
      return;
    }

    if (formData.password !== formData.confirmPassword) {
      setError('Les mots de passe ne correspondent pas');
      setLoading(false);
      return;
    }

    const passwordErrors = validatePassword(formData.password);
    if (passwordErrors.length > 0) {
      setError(`Mot de passe invalide: ${passwordErrors.map(e => e.message).join(', ')}`);
      setLoading(false);
      return;
    }

    try {
      console.log('Appel de la fonction register...');

      const registrationData = {
        name: formData.lastName.trim(),
        first_name: formData.firstName.trim(),
        email: formData.email.trim().toLowerCase(),
        password: formData.password,
        password_confirmation: formData.confirmPassword,
        phone1: formData.phone.trim() || 'Non renseigné',
        address: 'Non renseignée' // Valeur par défaut car le champ est requis en base
      };

      console.log('Données envoyées:', registrationData);
      console.log('Type de register:', typeof register);
      console.log('Register function:', register);

      const result = await register(registrationData);

      console.log('Résultat reçu:', result);
      console.log('Type du résultat:', typeof result);

      if (result?.success) {
        console.log('✅ Inscription réussie');
        console.log('Message du serveur:', result.message);
        console.log('Données utilisateur:', result.user);
        toast.success(result.message || 'Compte créé avec succès! Votre compte est en attente d\'approbation.');

        // Rediriger vers la page de connexion car l'utilisateur n'est pas connecté automatiquement
        setTimeout(() => {
          console.log('Redirection vers /login...');
          navigate('/login');
        }, 2000); // Plus de temps pour lire le message
      } else {
        console.log('❌ Échec de l\'inscription:', result);
        const errorMessage = result?.error || result?.message || 'Erreur lors de la création du compte';
        console.log('Message d\'erreur:', errorMessage);
        setError(errorMessage);
        toast.error(errorMessage);
      }
    } catch (err) {
      console.error('❌ Erreur lors de l\'inscription:', err);
      console.log('Détails de l\'erreur:', {
        message: err.message,
        response: err.response,
        data: err.response?.data,
        status: err.response?.status
      });
      const errorMessage = err.message || 'Erreur lors de la création du compte';
      setError(errorMessage);
      toast.error(errorMessage);
    } finally {
      setLoading(false);
      console.log('=== FIN DE L\'INSCRIPTION ===');
    }
  };

  return (
    <RegisterContainer>
      <RegisterCard>
        <Title>Créer un compte</Title>

        {error && <ErrorMessage>{error}</ErrorMessage>}

        <Form onSubmit={handleSubmit}>
          <FormRow>
            <FormGroup>
              <Label htmlFor="firstName">Prénom *</Label>
              <Input
                type="text"
                id="firstName"
                name="firstName"
                value={formData.firstName}
                onChange={handleChange}
                required
                placeholder="Votre prénom"
              />
            </FormGroup>

            <FormGroup>
              <Label htmlFor="lastName">Nom *</Label>
              <Input
                type="text"
                id="lastName"
                name="lastName"
                value={formData.lastName}
                onChange={handleChange}
                required
                placeholder="Votre nom"
              />
            </FormGroup>
          </FormRow>

          <FormGroup>
            <Label htmlFor="email">Email *</Label>
            <Input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
              placeholder="votre@email.com"
            />
          </FormGroup>

          <FormGroup>
            <Label htmlFor="phone">Téléphone</Label>
            <Input
              type="tel"
              id="phone"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              placeholder="Votre numéro de téléphone"
            />
          </FormGroup>

          <FormGroup>
            <Label htmlFor="password">Mot de passe *</Label>
            <Input
              type="password"
              id="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              required
              placeholder="Votre mot de passe"
            />
            <PasswordRequirements>
              Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.
            </PasswordRequirements>
          </FormGroup>

          <FormGroup>
            <Label htmlFor="confirmPassword">Confirmer le mot de passe *</Label>
            <Input
              type="password"
              id="confirmPassword"
              name="confirmPassword"
              value={formData.confirmPassword}
              onChange={handleChange}
              required
              placeholder="Confirmez votre mot de passe"
            />
          </FormGroup>

          <Button type="submit" disabled={loading}>
            {loading ? 'Création...' : 'Créer le compte'}
          </Button>
        </Form>

        <LinkContainer>
          <p>Déjà un compte ?</p>
          <StyledLink to="/login">Se connecter</StyledLink>
        </LinkContainer>
      </RegisterCard>
    </RegisterContainer>
  );
};

export default RegisterPage;
